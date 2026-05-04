# Git 初始化与首个提交说明

这份说明面向准备把 `FFMeet` 第一次发布到 GitHub 或其他 Git 托管平台的维护者。

## 使用场景

适用于以下情况：

- 当前目录还没有作为正式公开仓库初始化
- 需要把整理后的 `v1.0.0` 基线推送到新仓库
- 需要一个统一的首个提交口径

## 初始化前先确认

在执行 `git init` 之前，建议先确认：

- `README.md` 已更新为对外说明
- `LICENSE` 已确认
- `.gitignore` 已覆盖环境文件、数据库、日志、上传文件和构建产物
- `docs/` 已可公开阅读
- `composer package:source` 可成功生成安全源码包

如需完整检查项，请先阅读：

- [公开仓库前检查清单](./public-repository-checklist.md)
- [GitHub 仓库命名与首次发布说明](./github-repository-launch-guide.md)

## 推荐初始化步骤

如果当前目录还不是正式 Git 仓库，可按以下步骤执行：

```bash
git init
git branch -M main
git add .
git commit -m "chore: prepare FFMeet v1.0.0 public baseline"
```

然后添加远程仓库：

```bash
git remote add origin <your-repository-url>
git push -u origin main
```

其中 `<your-repository-url>` 应替换成实际仓库地址，例如：

- `git@github.com:your-org/ffmeet.git`
- `https://github.com/your-org/ffmeet.git`

## 推荐首个提交文案

建议首个提交直接使用：

```text
chore: prepare FFMeet v1.0.0 public baseline
```

这个文案的含义是：

- 当前提交的目标是整理公开基线
- 不把它误表述成“功能新增”
- 方便后续从 `v1.0.0` 开始继续维护 changelog 和 release

## 推荐首个 Tag

如果仓库状态已经准备好，可以继续打第一个版本标签：

```bash
git tag -a v1.0.0 -m "FFMeet v1.0.0"
git push origin v1.0.0
```

## 首次推送前建议再检查

在执行 `git push` 之前，建议至少确认：

- `git status` 没有意外的本机文件
- `.env` 没有被跟踪
- `database/*.sqlite` 没有被跟踪
- `storage/logs/*` 没有被跟踪
- `public/storage/*` 没有被跟踪
- `dist/*.tar.gz`、`dist/*.sha256` 没有被跟踪

可使用：

```bash
git status --short
```

快速核对待提交文件。

## 推荐仓库首次发布顺序

建议按以下顺序执行：

1. 本地整理 `v1.0.0` 基线
2. 初始化 Git 并完成首个提交
3. 推送到私有仓库
4. 在私有仓库内再次检查 README、LICENSE、docs、源码包流程
5. 确认无误后切换为公开仓库
6. 创建 `v1.0.0` Release

## 不建议的做法

不建议直接执行以下动作：

- 把当前工作目录未经检查直接推送到公开仓库
- 在没有 `LICENSE` 的情况下直接公开
- 把 `.env`、SQLite 数据库、日志或上传目录纳入首个提交
- 把本地产生的源码包和校验文件直接提交到仓库
